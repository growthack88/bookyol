import { useRef } from 'react';
import { ChevronLeft, ChevronRight } from 'lucide-react';
import { authors } from '@/data/mockData';
import { useInView } from '@/hooks/useInView';

const AuthorStrip = () => {
  const scrollRef = useRef<HTMLDivElement>(null);
  const [sectionRef, isInView] = useInView<HTMLElement>({ threshold: 0.2 });

  const scroll = (direction: 'left' | 'right') => {
    if (scrollRef.current) {
      const amount = direction === 'left' ? -250 : 250;
      scrollRef.current.scrollBy({ left: amount, behavior: 'smooth' });
    }
  };

  return (
    <section
      ref={sectionRef}
      className={`py-12 transition-all duration-700 ${
        isInView ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-8'
      }`}
      aria-labelledby="authors-title"
    >
      <div className="container mx-auto px-4">
        <div className="mb-6 flex items-center justify-between">
          <div>
            <h2 id="authors-title" className="text-2xl font-bold text-foreground">
              مؤلفون مميزون
            </h2>
            <p className="text-muted-foreground">تعرّف على أبرز الكتّاب</p>
          </div>
          <div className="hidden gap-2 md:flex">
            <button
              onClick={() => scroll('right')}
              className="rounded-full border border-border bg-card p-2 transition-all hover:bg-accent"
              aria-label="التمرير لليمين"
            >
              <ChevronRight className="h-5 w-5" />
            </button>
            <button
              onClick={() => scroll('left')}
              className="rounded-full border border-border bg-card p-2 transition-all hover:bg-accent"
              aria-label="التمرير لليسار"
            >
              <ChevronLeft className="h-5 w-5" />
            </button>
          </div>
        </div>

        <div
          ref={scrollRef}
          className="scrollbar-hide flex gap-4 overflow-x-auto pb-2"
        >
          {authors.map((author, index) => (
            <article
              key={author.id}
              className="group shrink-0 w-64 rounded-2xl border border-border bg-card p-5 transition-all duration-300 hover:-translate-y-1 hover:shadow-lg"
              style={{ animationDelay: `${index * 50}ms` }}
            >
              <div className="flex items-center gap-4 mb-4">
                <div
                  className={`flex h-14 w-14 items-center justify-center rounded-full ${author.avatarColor} text-xl font-bold text-white transition-transform duration-300 group-hover:scale-105`}
                >
                  {author.name.charAt(0)}
                </div>
                <div>
                  <h3 className="font-semibold text-foreground">{author.name}</h3>
                  <span className="inline-block rounded-full bg-muted px-2 py-0.5 text-xs text-muted-foreground">
                    {author.style}
                  </span>
                </div>
              </div>
              <p className="text-sm text-muted-foreground">
                ابدأ بـ: <span className="font-medium text-foreground">{author.starterBook}</span>
              </p>
            </article>
          ))}
        </div>
      </div>
    </section>
  );
};

export default AuthorStrip;
