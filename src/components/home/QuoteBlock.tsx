import { quote } from '@/data/mockData';
import { useInView } from '@/hooks/useInView';
import { Quote, ArrowLeft } from 'lucide-react';

const QuoteBlock = () => {
  const [sectionRef, isInView] = useInView<HTMLElement>({ threshold: 0.2 });

  return (
    <section
      ref={sectionRef}
      className={`py-12 transition-all duration-700 ${
        isInView ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-8'
      }`}
    >
      <div className="container mx-auto px-4">
        <div className="relative overflow-hidden rounded-3xl border border-border bg-gradient-to-bl from-primary/5 to-transparent p-8 md:p-12">
          {/* Decorative quote icon */}
          <Quote className="absolute left-6 top-6 h-20 w-20 text-primary/10 md:h-32 md:w-32" />

          <div className="relative">
            <p className="mb-3 text-sm font-medium text-primary">اقتباس اليوم</p>
            <blockquote className="mb-6 text-xl font-medium leading-relaxed text-foreground md:text-2xl md:leading-relaxed">
              "{quote.text}"
            </blockquote>
            <div className="flex flex-wrap items-center justify-between gap-4">
              <div>
                <p className="font-semibold text-foreground">{quote.author}</p>
                <p className="text-sm text-muted-foreground">من كتاب: {quote.book}</p>
              </div>
              <button className="flex items-center gap-2 rounded-xl bg-primary px-5 py-2.5 font-medium text-primary-foreground transition-colors hover:bg-primary/90">
                اقرأ سياق الاقتباس
                <ArrowLeft className="h-4 w-4" />
              </button>
            </div>
          </div>
        </div>
      </div>
    </section>
  );
};

export default QuoteBlock;
