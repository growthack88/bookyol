import { useState } from "react";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import FloatingIcons from "@/components/FloatingIcons";
import { supabase } from "@/integrations/supabase/client";
import logo from "@/assets/bookyol-logo.png";
import { z } from "zod";

const emailSchema = z.string().trim().email({ message: "Please enter a valid email" }).max(255);

const Index = () => {
  const [email, setEmail] = useState("");
  const [status, setStatus] = useState<"idle" | "loading" | "success" | "error">("idle");
  const [errorMessage, setErrorMessage] = useState("");

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setErrorMessage("");
    
    // Validate email
    const result = emailSchema.safeParse(email);
    if (!result.success) {
      setErrorMessage(result.error.errors[0].message);
      return;
    }

    setStatus("loading");
    
    try {
      const { error } = await supabase
        .from("newsletter_signups")
        .insert({ email: result.data });

      if (error) {
        if (error.code === "23505") {
          // Unique constraint violation - email already exists
          setStatus("success");
        } else {
          throw error;
        }
      } else {
        setStatus("success");
      }
    } catch {
      setStatus("error");
      setErrorMessage("Something went wrong. Please try again.");
    }
  };

  return (
    <div className="relative h-screen w-screen overflow-hidden bg-gradient-to-br from-background via-background to-secondary">
      <FloatingIcons />
      
      {/* Center content */}
      <div className="relative z-10 flex h-full w-full items-center justify-center px-6">
        <div className="flex flex-col items-center text-center">
          {/* Logo */}
          <img 
            src={logo} 
            alt="BookYol.com - Read. Discover. Escape." 
            className="mb-8 h-auto w-56 sm:w-72 md:w-80"
          />
          
          {/* Headline */}
          <h1 className="mb-3 text-3xl font-semibold tracking-tight text-foreground sm:text-4xl">
            Coming Soon
          </h1>
          
          {/* Subtext */}
          <p className="mb-8 max-w-md text-base font-light text-muted-foreground sm:text-lg">
            A new home for readers is on the way.
          </p>
          
          {/* Newsletter form */}
          {status === "success" ? (
            <div className="rounded-lg border border-primary/20 bg-primary/5 px-6 py-3">
              <p className="text-sm font-medium text-primary">
                You're on the list.
              </p>
            </div>
          ) : (
            <form 
              onSubmit={handleSubmit}
              className="flex w-full max-w-sm flex-col gap-3 sm:flex-row"
            >
              <div className="flex-1">
                <Input
                  type="email"
                  placeholder="Enter your email"
                  value={email}
                  onChange={(e) => setEmail(e.target.value)}
                  required
                  disabled={status === "loading"}
                  className="h-11 w-full border-border bg-card/80 font-light backdrop-blur-sm"
                />
                {errorMessage && (
                  <p className="mt-1 text-xs text-destructive">{errorMessage}</p>
                )}
              </div>
              <Button 
                type="submit"
                disabled={status === "loading"}
                className="h-11 px-6 font-medium"
              >
                {status === "loading" ? "..." : "Notify Me"}
              </Button>
            </form>
          )}
        </div>
      </div>
    </div>
  );
};

export default Index;
