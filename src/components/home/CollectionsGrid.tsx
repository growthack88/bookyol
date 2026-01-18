import { collections } from '@/data/mockData';
import { useInView } from '@/hooks/useInView';
import { ArrowLeft } from 'lucide-react';

const CollectionsGrid = () => {
  const [sectionRef, isInView] = useInView<HTMLElement>({ threshold: 0.1 });

  return (
    <section
      ref={sectionRef}
      className={`py-12 transition-all duration-700 ${
        isInView ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-8'
      }`}
      aria-labelledby="collections-title"
    >
      <div className="container mx-auto px-4">
        <div className="mb-6">
          <h2 id="collections-title" className="text-2xl font-bold text-foreground">
            قوائم مختارة
          </h2>
          <p className="text-muted-foreground">مجموعات كتب منتقاة بعناية</p>
        </div>

        <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
          {collections.map((collection, index) => (
            <article
              key={collection.id}
              className="group relative overflow-hidden rounded-2xl border border-border bg-card p-5 transition-all duration-300 hover:-translate-y-1 hover:shadow-lg"
              style={{ animationDelay: `${index * 50}ms` }}
            >
              {/* Mini covers stack */}
              <div className="mb-4 flex -space-x-3 space-x-reverse">
                {collection.colors.map((color, i) => (
                  <div
                    key={i}
                    className={`h-16 w-11 rounded-lg ${color} border-2 border-card shadow-sm transition-transform duration-300 group-hover:translate-y-[-2px]`}
                    style={{ 
                      transform: `rotate(${(i - 1) * 5}deg)`,
                      zIndex: 3 - i 
                    }}
                  />
                ))}
              </div>

              <h3 className="mb-1 text-lg font-semibold text-foreground">
                {collection.title}
              </h3>
              <p className="mb-3 text-sm text-muted-foreground">
                {collection.hook}
              </p>
              <div className="flex items-center justify-between">
                <span className="text-sm text-muted-foreground">
                  {collection.count} كتاب
                </span>
                <button className="flex items-center gap-1 text-sm font-medium text-primary transition-colors hover:text-primary/80">
                  استعرض
                  <ArrowLeft className="h-4 w-4" />
                </button>
              </div>
            </article>
          ))}
        </div>
      </div>
    </section>
  );
};

export default CollectionsGrid;
